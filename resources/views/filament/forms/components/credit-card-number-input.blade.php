@php
    $fieldWrapperView = $getFieldWrapperView();
    $extraAttributes = $getExtraAttributeBag()
        ->merge($getExtraAlpineAttributes(), escape: false)
        ->merge([
            'autocomplete' => 'cc-number',
            'autofocus' => $isAutofocused(),
            'disabled' => $isDisabled(),
            'id' => $getId(),
            'placeholder' => $getPlaceholder() ?? '1234 5678 9012 3456',
            'readonly' => $isReadOnly(),
            'required' => $isRequired() && (! $isConcealed()),
        ], escape: false);
@endphp

<x-dynamic-component :component="$fieldWrapperView" :field="$field">
    <div
        x-data="{
            state: @entangle($getStatePath()),
            isMasked: @js($isMasked()),
            isInputFocused: false,
            
            get inputValue() {
                const digits = (this.state || '').replace(/\D/g, '')
                return this.formatDisplayValue(digits)
            },
            
            formatDisplayValue(digits) {
                // Only mask when not focused AND isMasked is true AND we have 16 digits
                if (this.isMasked && !this.isInputFocused && digits.length === 16) {
                    // Show only last 4 digits: •••• •••• •••• 1234
                    const masked = '•'.repeat(12)
                    const visible = digits.slice(-4)
                    const combined = masked + visible
                    return combined.replace(/(.{4})/g, '$1 ').trim()
                } else {
                    // Show all digits: 1234 5678 9012 3456
                    return digits.replace(/(.{4})/g, '$1 ').trim()
                }
            },
            
            handleInput(event) {
                const input = event.target
                const value = input.value
                const cursorPos = input.selectionStart
                
                // Extract digits only
                const allDigits = value.replace(/\D/g, '')
                const limitedDigits = allDigits.slice(0, 16)
                
                // Update state - this will trigger reactive update
                this.state = limitedDigits
                
                // Calculate where cursor should be after reactive update
                const digitsBefore = value.slice(0, cursorPos).replace(/\D/g, '').length
                
                // Wait for reactive update, then position cursor
                this.$nextTick(() => {
                    const newDisplayValue = this.formatDisplayValue(limitedDigits)
                    let newCursorPos = 0
                    let digitCount = 0
                    
                    for (let i = 0; i < newDisplayValue.length && digitCount < digitsBefore; i++) {
                        const char = newDisplayValue[i]
                        if (char !== ' ' && char !== '•') {
                            digitCount++
                        }
                        newCursorPos = i + 1
                    }
                    
                    input.setSelectionRange(newCursorPos, newCursorPos)
                })
            },
            
            handleKeyDown(event) {
                // Allow navigation and control keys
                const allowedKeys = [8, 9, 27, 13, 46, 35, 36, 37, 38, 39, 40]
                if (allowedKeys.includes(event.keyCode)) return
                
                // Allow Ctrl combinations
                if (event.ctrlKey && [65, 67, 86, 88].includes(event.keyCode)) return
                
                // Only allow numbers
                const isNumber = (event.keyCode >= 48 && event.keyCode <= 57) || 
                                (event.keyCode >= 96 && event.keyCode <= 105)
                
                if (!isNumber || event.shiftKey) {
                    event.preventDefault()
                }
            },
            
            handlePaste(event) {
                event.preventDefault()
                const pastedText = (event.clipboardData || window.clipboardData).getData('text')
                const digits = pastedText.replace(/\D/g, '').slice(0, 16)
                this.state = digits
            }
        }"
        {{
            $extraAttributes
                ->class([
                    'fi-credit-card-number-input',
                ])
        }}
    >
        <!-- Single credit card number input -->
        <input
            type="text"
            x-ref="creditCardNumberInput"
            inputmode="numeric"
            pattern="[0-9 ]*"
            maxlength="19"
            :placeholder="@js($getPlaceholder() ?? '1234 5678 9012 3456')"
            :value="inputValue"
            @input="handleInput($event)"
            @keydown="handleKeyDown($event)"
            @paste="handlePaste($event)"
            @focus="isInputFocused = true"
            @blur="isInputFocused = false"
            :disabled="@js($isDisabled())"
            :readonly="@js($isReadOnly())"
            {{
                $extraAttributes
                    ->class([
                        'w-full px-3 py-2 font-mono text-lg tracking-wider',
                        'border border-gray-300 rounded-lg',
                        'bg-white dark:bg-gray-800',
                        'text-gray-900 dark:text-gray-100',
                        'placeholder:text-gray-400 dark:placeholder:text-gray-500',
                        'focus:border-primary-500 focus:ring-1 focus:ring-primary-500',
                        'disabled:bg-gray-50 disabled:text-gray-500 disabled:cursor-not-allowed',
                        'dark:border-gray-600 dark:disabled:bg-gray-900 dark:disabled:text-gray-400',
                        'transition-colors duration-200',
                    ])
            }}
        />
        
        <!-- Hidden input for form submission with raw digits -->
        <input
            type="hidden"
            :name="@js($getStatePath())"
            :value="state"
        />
    </div>
</x-dynamic-component>
